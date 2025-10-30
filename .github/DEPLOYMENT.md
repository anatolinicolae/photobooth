# GitHub Deployment

This repository includes a GitHub Actions workflow for automatic deployment of the React web app.

## Workflow: Build and Deploy React App

**File**: `.github/workflows/deploy.yml`

### Triggers

- Push to `main` branch
- Push to `anatoli/deploy-to-prod` branch
- Manual trigger via GitHub Actions UI

### What it does

1. **Checkout code** - Gets the latest code from the repository
2. **Setup Node.js** - Installs Node.js 18 with npm caching
3. **Install dependencies** - Runs `npm ci` in the webapp directory
4. **Build React app** - Creates production build in `webapp/build/`
5. **Deploy via SFTP** - Transfers build assets to remote server

### Required Secrets

You need to configure the following secrets in your GitHub repository settings:

**Settings → Secrets and variables → Actions → New repository secret**

| Secret Name | Description | Example |
|-------------|-------------|---------|
| `SFTP_HOST` | Server hostname or IP address | `example.com` or `192.168.1.100` |
| `SFTP_USER` | SSH/SFTP username | `deploy` or `www-data` |
| `SFTP_PASS` | SSH/SFTP password | `your-secure-password` |

### Configuration Options

You can customize the deployment by editing `.github/workflows/deploy.yml`:

#### Remote Path
Change where files are deployed on the server:
```yaml
remote_path: '/var/www/html'  # Change to your web root
```

Common paths:
- `/var/www/html` - Default Apache/Nginx web root
- `/home/user/public_html` - User web directory
- `/usr/share/nginx/html` - Alternative Nginx location

#### Delete Remote Files
To clear the remote directory before deployment:
```yaml
delete_remote_files: true  # Currently set to false
```

⚠️ **Warning**: Setting this to `true` will delete all files in `remote_path` before uploading!

#### Branches
Add or remove deployment branches:
```yaml
branches:
  - main
  - production
  - staging
```

### Manual Deployment

1. Go to **Actions** tab in GitHub
2. Select **Build and Deploy React App** workflow
3. Click **Run workflow** button
4. Choose branch and click **Run workflow**

### Viewing Deployment Status

- **Actions tab**: See all workflow runs and their status
- **Commit badges**: Green checkmark = successful deployment
- **Logs**: Click on any workflow run to see detailed logs

### Troubleshooting

#### Build fails
- Check Node.js version compatibility
- Verify `package.json` and dependencies
- Review build logs in Actions tab

#### SFTP connection fails
- Verify `SFTP_HOST` is correct and accessible
- Check `SFTP_USER` has proper permissions
- Ensure `SFTP_PASS` is correct
- Check if firewall allows SFTP (port 22)

#### Files not appearing on server
- Verify `remote_path` exists on server
- Check user permissions for the remote directory
- Ensure `local_path` pattern is correct (`./webapp/build/*`)

#### Permission denied on server
```bash
# On your server, ensure proper permissions:
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
```

### Security Best Practices

1. **Use SSH keys instead of passwords** (recommended):
   - Generate SSH key pair
   - Add public key to server's `~/.ssh/authorized_keys`
   - Store private key as `SFTP_SSH_PRIVATE_KEY` secret
   - Update workflow to use `ssh_private_key` instead of `password`

2. **Restrict user permissions**:
   - Create dedicated deployment user
   - Limit access to only the web directory
   - Use SFTP-only chroot jail if possible

3. **Rotate credentials regularly**:
   - Update secrets periodically
   - Monitor deployment logs for suspicious activity

### Alternative: SSH Key Authentication

For better security, use SSH keys instead of passwords:

```yaml
- name: Deploy to server via SFTP
  uses: wlixcc/SFTP-Deploy-Action@v1.2.4
  with:
    username: ${{ secrets.SFTP_USER }}
    server: ${{ secrets.SFTP_HOST }}
    ssh_private_key: ${{ secrets.SFTP_SSH_PRIVATE_KEY }}
    local_path: './webapp/build/*'
    remote_path: '/var/www/html'
    sftp_only: true
```

Then add `SFTP_SSH_PRIVATE_KEY` secret with your private key content.

## Monitoring Deployments

Each deployment will:
- Show status in the Actions tab
- Add a commit status check
- Log all steps for debugging
- Report success/failure

Check deployment logs regularly to ensure smooth operations.
